# Security Tests

The following tests are made to ensure, that Ember Nexus' security model is working as intended.

## Basic Negative Tests

### Test 1-01: No Connection

Users who are not connected to nodes in any way, can not have access to them:

<div id="test-1-01" class="graph-container" style="height:300px"></div>

### Test 1-02: No Relevant Connection

Users which are connected to nodes with relationships, which are not relevant for the security system, do not have
access. Relevant relations include `OWNS`, `HAS_X_ACCESS` and `CREATED`.

<div id="test-1-02" class="graph-container" style="height:300px"></div>

### Test 1-03: Missing access to either the start or end node of relationships

If a user has the required access to either the start or end node of a relationship but not the other, then he does not
have the access required to work with the relationship.

In the following example, the user can not access the `RELATIONSHIP`-relationship, as he does not have access to the
second data node.

<div id="test-1-03" class="graph-container" style="height:400px"></div>

### Test 1-04: OWNS-relationships are directional

`OWNS`-relationships are directional, i.e. users who have access to an owned element do not automatically have access to
its parents.

In the following example, user 1 has access to both data nodes, while user 2 can only access the second data node. The
`OWNS`-relationship between data 1 and data 2 is directional, i.e. can only grant the owners of data 1 access (user 1),
while the relationship is ignored for owners of data 2 (user 2):

<div id="test-1-04" class="graph-container" style="height:400px"></div>

## Basic Positive Test

### Test 2-01: Immediate Node Ownership

Users which immediately own a node, have full access to it.

<div id="test-2-01" class="graph-container" style="height:300px"></div>

### Test 2-02: Immediate Relation Ownership

Users which immediately own both the start and end node of a relationship, have full access to the relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-2-02" class="graph-container" style="height:400px"></div>

### Test 2-03: Path Node Ownership

Users which are connected to a node through multiple chained `OWNS` relations have full access to the node:

<div id="test-2-03" class="graph-container" style="height:300px"></div>

### Test 2-04: Path Relation Ownership

Users which are connected to both the start and end node of a relationship through multiple chained `OWNS` relations
have full access to the relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-2-04" class="graph-container" style="height:400px"></div>

## Single Group Positive Test

### Test 3-01: Immediate Node Ownership

Users of groups which immediately own a node, have full access to it.

<div id="test-3-01" class="graph-container" style="height:300px"></div>

### Test 3-02: Immediate Relation Ownership

Users of Groups which immediately own both the start and end node of a relationship, have full access to the
relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-3-02" class="graph-container" style="height:400px"></div>

### Test 3-03: Path Node Ownership

Users of Groups which are connected to a node through multiple chained `OWNS` relations have full access to the node:

<div id="test-3-03" class="graph-container" style="height:300px"></div>

### Test 3-04: Path Relation Ownership

Users of groups which are connected to both the start and end node of a relationship through multiple chained `OWNS`
relations have full access to the relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-3-04" class="graph-container" style="height:400px"></div>

## Multiple Group Positive Test

### Test 4-01: Immediate Node Ownership

Users which are in a chain of groups, where the last group immediately owns a node, have full access to it.

<div id="test-4-01" class="graph-container" style="height:300px"></div>

### Test 4-02: Immediate Relation Ownership

Users which are in a chain of groups, where the last group immediately own both the start and end node of a
relationship, have full access to the relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-4-02" class="graph-container" style="height:400px"></div>

### Test 4-03: Path Node Ownership

Users which are in a chain of groups, where the last group is connected to a node through multiple chained `OWNS`
relations have full access to the node:

<div id="test-4-03" class="graph-container" style="height:300px"></div>

### Test 4-04: Path Relation Ownership

Users which are in a chain of groups, where the last group is connected to both the start and end node of a relationship
through multiple chained `OWNS` relations have full access to the relationship.

In the example, the user has full access to the relationship with the type `RELATIONSHIP`.

<div id="test-4-04" class="graph-container" style="height:400px"></div>

## Mixed Group Ownership

### Test 5-01: Mixed Node Ownership

If there are multiple paths of `OWNS`-relations between the user, possible groups and the node, then the user has full
access to the node.

<div id="test-5-01" class="graph-container" style="height:400px"></div>

### Test 5-02: Mixed Relation Ownership

If there are multiple paths of `OWNS`-relations between the user, possible groups and the start and end node of the
relation, then the user has full access to the relation.

<div id="test-5-02" class="graph-container" style="height:400px"></div>

## Edge Cases

### Test 99-01: IS_IN_GROUP after OWNS have no effect

If an `IS_IN_GROUP` relationship appears after at least one `OWNS` relationship, then the `IS_IN_GROUP` relationship
must be ignored / will be handled like any non security related relationship.

In the following example, the user does not have any access to group 2 or data 2, as there are no `OWNS`-paths between
the user and those nodes.

<div id="test-99-01" class="graph-container" style="height:400px"></div>

### Test 99-02: Owning groups give direct access but not to related groups

If users own groups directly or through other groups, then they also own the elements the group owns.  
**However, they do not inherit access to groups connected via `IS_IN_GROUP` relationships.**

In the following example, the user has owning access to the data nodes 1 and 2, as there is a direct chain of
`OWNS`-relations. However, the user does not have access to data node 3.

<div id="test-99-02" class="graph-container" style="height:400px"></div>

<script>
renderGraph(document.getElementById('test-1-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data', ...dataNode },
  ],
  edges: []
}, 'TB');
renderGraph(document.getElementById('test-1-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data', ...dataNode },
  ],
  edges: [
    { source: 'user', target: 'data', label: 'SOME_RELATION' },
  ]
});
renderGraph(document.getElementById('test-1-03'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-1-04'), {
  nodes: [
    { id: 'user1', ...userNode, label: 'User 1' },
    { id: 'user2', ...userNode, label: 'User 2' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user1', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'user2', target: 'data2', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-2-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data', ...dataNode },
  ],
  edges: [
    { source: 'user', target: 'data', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-2-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user', target: 'data1', label: 'OWNS' },
    { source: 'user', target: 'data2', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-2-03'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
  ],
  edges: [
    { source: 'user', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-2-04'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
    { id: 'data4', ...dataNode, label: 'Data 4' },
    { id: 'data5', ...dataNode, label: 'Data 5' },
    { id: 'data6', ...dataNode, label: 'Data 6' },
  ],
  edges: [
    { source: 'user', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
    { source: 'user', target: 'data4', label: 'OWNS' },
    { source: 'data4', target: 'data5', label: 'OWNS' },
    { source: 'data5', target: 'data6', label: 'OWNS' },
    { source: 'data3', target: 'data6', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-3-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group', ...groupNode },
    { id: 'data', ...dataNode },
  ],
  edges: [
    { source: 'user', target: 'group', label: 'IS_IN_GROUP' },
    { source: 'group', target: 'data', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-3-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group', ...groupNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user', target: 'group', label: 'IS_IN_GROUP' },
    { source: 'group', target: 'data1', label: 'OWNS' },
    { source: 'group', target: 'data2', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-3-03'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group', ...groupNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
  ],
  edges: [
    { source: 'user', target: 'group', label: 'IS_IN_GROUP' },
    { source: 'group', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-3-04'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group', ...groupNode },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
    { id: 'data4', ...dataNode, label: 'Data 4' },
    { id: 'data5', ...dataNode, label: 'Data 5' },
    { id: 'data6', ...dataNode, label: 'Data 6' },
  ],
  edges: [
    { source: 'user', target: 'group', label: 'IS_IN_GROUP' },
    { source: 'group', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
    { source: 'group', target: 'data4', label: 'OWNS' },
    { source: 'data4', target: 'data5', label: 'OWNS' },
    { source: 'data5', target: 'data6', label: 'OWNS' },
    { source: 'data3', target: 'data6', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-4-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'group3', ...groupNode, label: 'Group 3' },
    { id: 'data', ...dataNode },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'group3', label: 'IS_IN_GROUP' },
    { source: 'group3', target: 'data', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-4-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'group3', ...groupNode, label: 'Group 3' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'group3', label: 'IS_IN_GROUP' },
    { source: 'group3', target: 'data1', label: 'OWNS' },
    { source: 'group3', target: 'data2', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-4-03'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'group3', ...groupNode, label: 'Group 3' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'group3', label: 'IS_IN_GROUP' },
    { source: 'group3', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
  ]
});
renderGraph(document.getElementById('test-4-04'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'group3', ...groupNode, label: 'Group 3' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
    { id: 'data4', ...dataNode, label: 'Data 4' },
    { id: 'data5', ...dataNode, label: 'Data 5' },
    { id: 'data6', ...dataNode, label: 'Data 6' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'group3', label: 'IS_IN_GROUP' },
    { source: 'group3', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
    { source: 'group3', target: 'data4', label: 'OWNS' },
    { source: 'data4', target: 'data5', label: 'OWNS' },
    { source: 'data5', target: 'data6', label: 'OWNS' },
    { source: 'data3', target: 'data6', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-5-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
    { source: 'user', target: 'data2', label: 'OWNS' }
  ]
});
renderGraph(document.getElementById('test-5-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
    { id: 'data4', ...dataNode, label: 'Data 4' },
    { id: 'data5', ...dataNode, label: 'Data 5' },
    { id: 'data6', ...dataNode, label: 'Data 6' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'data2', label: 'OWNS' },
    { source: 'data2', target: 'data3', label: 'OWNS' },
    { source: 'user', target: 'data4', label: 'OWNS' },
    { source: 'data4', target: 'data5', label: 'OWNS' },
    { source: 'data5', target: 'data6', label: 'OWNS' },
    { source: 'data3', target: 'data6', label: 'RELATIONSHIP' },
  ]
});
renderGraph(document.getElementById('test-99-01'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'data1', label: 'OWNS' },
    { source: 'data1', target: 'group2', label: 'IS_IN_GROUP' },
    { source: 'group2', target: 'data2', label: 'OWNS' }
  ]
});
renderGraph(document.getElementById('test-99-02'), {
  nodes: [
    { id: 'user', ...userNode },
    { id: 'group1', ...groupNode, label: 'Group 1' },
    { id: 'group2', ...groupNode, label: 'Group 2' },
    { id: 'group3', ...groupNode, label: 'Group 3' },
    { id: 'data1', ...dataNode, label: 'Data 1' },
    { id: 'data2', ...dataNode, label: 'Data 2' },
    { id: 'data3', ...dataNode, label: 'Data 3' },
  ],
  edges: [
    { source: 'user', target: 'group1', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'group2', label: 'OWNS' },
    { source: 'group2', target: 'group3', label: 'IS_IN_GROUP' },
    { source: 'group1', target: 'data1', label: 'OWNS' },
    { source: 'group2', target: 'data2', label: 'OWNS' },
    { source: 'group3', target: 'data3', label: 'OWNS' }
  ]
});
</script>